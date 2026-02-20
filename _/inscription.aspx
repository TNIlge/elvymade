<%@ Page Title="Inscription" Language="C#" MasterPageFile="~/Site.Master" AutoEventWireup="true" CodeBehind="inscription.aspx.cs" Inherits="ISTMADCAMER.inscription" %>
<asp:Content ID="Content1" ContentPlaceHolderID="MainContent" runat="server">
     <div class="stricky-header stricked-menu main-menu main-menu-one--two">
            <div class="sticky-header__content"></div><!-- /.sticky-header__content -->
        </div><!-- /.stricky-header -->


     <!--Start Breadcrumb Style1-->
        <section class="breadcrumb-style1 blog-details">
            <div class="breadcrumb-style1__bg"
                style="background-image: url(mad-assets/images/backgrounds/breadcrumb-v1-bg05.jpg);"></div>
            <div class="auto-container">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="breadcrumb-style1__inner text-center">
                            <h2>INSCRIPTIONS DEJA OUVERTES A IST MAD </h2>

                            <ul class="meta-box">
                                <li>
                                    <div class="inner">
                                        <div class="icon">
                                            <span class="icon-calendar-1"></span>
                                        </div>
                                        <div class="text">
                                            <p>Pour l'année Académique 2023 - 2024</p>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="inner">
                                        <div class="icon">
                                            <span class="icon-calendar-1"></span>
                                        </div>
                                        <div class="text">
                                            <p>Réduction de  25% aux inscrits avant le 1er Octobre</p>
                                        </div>
                                    </div>
                                </li>

                            </ul>                        
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--End Breadcrumb Style1-->



    
        <!--Start Contact Page-->
        <section class="contact-page">
            <div class="auto-container">
                <div class="row">
                   
                    <div class="col-xl-12 col-lg-12">
                        <!--End Contact Page Content-->
                     <asp:UpdatePanel runat="server" ID="ContactForm" ValidateRequestMode="Disabled">
                         <ContentTemplate>
                    <!--Start Contact Page Form-->

                        <div class="contact-page__form">
                            <div class="add-comment-box">
                                <div class="inner-title align-content-center">
                                    <h2>INSCRIVEZ-VOUS MAINTENANT</h2>
                                </div>
                                 <asp:Panel runat="server" ID="feedbackPanel" CssClass="alert">
                                     <asp:Label runat="server" ID="feedbackMsgLbl"></asp:Label>
                                 </asp:Panel>
                                 <asp:Panel runat="server" DefaultButton="inscriptionBtn">

                                <div id="contact-form" name="contact_form" class="default-form2">

                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <div class="input-box">
                                                    <asp:TextBox runat="server" ID="nameTB" CssClass="text-input" placeholder="Noms et Prénoms" TextMode="SingleLine"></asp:TextBox>
                                                     <ajaxToolkit:TextBoxWatermarkExtender runat="server" ID="nameTBWE" TargetControlID="nameTB" WatermarkCssClass="text-input" WatermarkText="Noms et Prénoms  *" />
                                                      <asp:RequiredFieldValidator runat="server" ID="nameTBRFV" ControlToValidate="nameTB" Display="Dynamic" ErrorMessage="Please enter this field" CssClass="alert alert-danger rfv" ValidationGroup="ContactForm"></asp:RequiredFieldValidator>
                                                   </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <div class="input-box">
                                                  <asp:TextBox runat="server" ID="emailTB" CssClass="text-input" placeholder="Votre e-mail *" TextMode="SingleLine"></asp:TextBox>
                                                     <ajaxToolkit:TextBoxWatermarkExtender runat="server" ID="emailTBWE" TargetControlID="emailTB" WatermarkCssClass="text-input" WatermarkText="Votre e-mail *" />
                                                       <asp:RequiredFieldValidator runat="server" ID="emailTBRFV" ControlToValidate="emailTB" Display="Dynamic" ErrorMessage="Please enter this field" CssClass="alert alert-danger rfv" ValidationGroup="ContactForm"></asp:RequiredFieldValidator>
                      
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <div class="input-box">
                                                    <asp:TextBox runat="server" ID="phoneNumTB" CssClass="text-input" placeholder="Numéro de téléphone" TextMode="SingleLine"></asp:TextBox>
                                                    <ajaxtoolkit:textboxwatermarkextender runat="server" ID="phoneNumTBWE" TargetControlID="phoneNumTB" WatermarkCssClass="text-input" WatermarkText="Numéro de téléphone" />

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="form-group">                                           
                                                        <asp:DropDownList ID="speciality" runat="server" CssClass="my-dropdown">
                                                        <asp:ListItem Text="Choisissez votre Spécialité *" Value=""></asp:ListItem>
                                                        <asp:ListItem Text="Commerce International " Value="Commerce International "></asp:ListItem>
                                                        <asp:ListItem Text="Marketing Commerce-vente" Value="Marketing Commerce-vente"></asp:ListItem>
                                                        <asp:ListItem Text="Communication Des Organisations" Value="Communication Des Organisations"></asp:ListItem>
                                                        <asp:ListItem Text="Télécommunications" Value="Télécommunications"></asp:ListItem>
                                                        <asp:ListItem Text="Réseaux et Sécurité" Value="Réseaux et Sécurité"></asp:ListItem>     
                                                        <asp:ListItem Text="Gestion Fiscale " Value="Douane et Transit "></asp:ListItem>
                                                        <asp:ListItem Text="Droit des Affaires et de L’entreprise" Value="Droit Foncier et Domanial"></asp:ListItem>
                                                        <asp:ListItem Text="Comptabilité & Gestion des Entreprises " Value="Comptabilité & Gestion des Entreprises"></asp:ListItem>                                                 
                                                        <asp:ListItem Text="Gestion des Projets" Value="Gestion des Projets "></asp:ListItem>                                                 
                                                        <asp:ListItem Text="Gestion des Ressources Humaines " Value="Gestion des Ressources Humaines"></asp:ListItem>                                                 
                                                        <asp:ListItem Text="Gestion de la Qualité " Value="Gestion de la Qualité  "></asp:ListItem>                                                 
                                                        <asp:ListItem Text="Gestion Logistique et Transport " Value="Gestion Logistique et Transport "></asp:ListItem>                                                 
                                                        <asp:ListItem Text="Gestion" Value="Gestion Logistique et Transport "></asp:ListItem>                                                 
                                                        <asp:ListItem Text="Gestion des Systèmes d’Information  " Value="Gestion des Systèmes d’Information "></asp:ListItem>                                                                                                                                                 
                                                        <asp:ListItem Text="Banque et Finance" Value="Banque et Finance"></asp:ListItem>                                                                                                                                                 
                                                        <asp:ListItem Text="Assurance" Value="Assurance"></asp:ListItem>                                                                                                                                                 
                                                        <asp:ListItem Text="Génie Logiciel " Value="Génie Logiciel "></asp:ListItem>                                                                                                                                                 
                                                        <asp:ListItem Text="Infographie et Web Design " Value="Infographie et Web Design "></asp:ListItem>                                                                                                                                                 
                                                        <asp:ListItem Text="Informatique Industriel et Automatisme " Value="Informatique Industriel et Automatisme "></asp:ListItem>                                                                                                                                                 
                                                        <asp:ListItem Text="Maintenance des Systèmes Informatiques " Value="Maintenance des Systèmes Informatiques "></asp:ListItem>                                                                                                                                                 
                                                        <asp:ListItem Text="E-commerce et Marketing Numérique" Value="E-commerce et Marketing Numérique"></asp:ListItem>                                                                                                                                                 
                                                    </asp:DropDownList> 
                                              
                                            </div>
                                        </div>  
                                    </div>
                                   
                                    <div class="row">
                                    
                                         <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="form-group">                                           
                                                        <asp:DropDownList ID="studyMode" runat="server" CssClass="my-dropdown">
                                                        <asp:ListItem Text="Types de Cours*" Value=""></asp:ListItem>
                                                        
                                                        <asp:ListItem Text="Cours du jour" Value="Cours du jour"></asp:ListItem>
                                                        <asp:ListItem Text="Cours du soir" Value="Cours du soir"></asp:ListItem>                                                                                                            
                                                    </asp:DropDownList> 
                                              
                                            </div>
                                        </div>
                                         <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="form-group">                                           
                                                        <asp:DropDownList ID="diplome" runat="server" CssClass="my-dropdown">
                                                        <asp:ListItem Text="Diplôme requis*" Value=""></asp:ListItem>
                                                        
                                                        <asp:ListItem Text="Baccalaureat" Value="Baccalaureat"></asp:ListItem>
                                                        <asp:ListItem Text="GCE A Level" Value="GCE A Level"></asp:ListItem>                                                                                                            
                                                    </asp:DropDownList> 
                                              
                                            </div>
                                        </div>
                                                                          
                                    </div>
                                     <br />
                                     <div class="row">
                                         <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="form-group">                                           
                                                        <asp:DropDownList ID="marriageStatus" runat="server" CssClass="my-dropdown">
                                                        <asp:ListItem Text="Situation Matrimonial * " Value=""></asp:ListItem>
                                                        
                                                        <asp:ListItem Text="Célibataire" Value="Celibataire"></asp:ListItem>
                                                        <asp:ListItem Text="Marié(e)" Value="Mariee"></asp:ListItem>                                                                                                            
                                                    </asp:DropDownList> 
                                              
                                            </div>
                                        </div>
                                         <div class="col-xl-6 col-lg-6 col-md-6">
                                            <div class="form-group">                                           
                                                        <asp:DropDownList ID="DropDownList2" runat="server" CssClass="my-dropdown">
                                                        <asp:ListItem Text="Sexe*" Value=""></asp:ListItem>
                                                        
                                                        <asp:ListItem Text="Masculin" Value="Masculin"></asp:ListItem>
                                                        <asp:ListItem Text="Feminin" Value="Feminin"></asp:ListItem>                                                                                                            
                                                    </asp:DropDownList> 
                                              
                                            </div>
                                        </div>
                                        
                                    </div>
                                     <br />
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="form-group">
                                                <div class="input-box">
                                                   <asp:TextBox runat="server" ID="messageTB" CssClass="text-input textArea" TextMode="MultiLine" placeholder="Autres Informations *" Height="145px"></asp:TextBox>
                                                   <ajaxToolkit:TextBoxWatermarkExtender runat="server" ID="messageTBWE" TargetControlID="messageTB" WatermarkCssClass="text-input" WatermarkText="Autres Informations" />
                                                    <asp:RequiredFieldValidator runat="server" ID="messageTBRFV" ControlToValidate="messageTB" Display="Dynamic" ErrorMessage="Please enter this field" CssClass="alert alert-danger rfv" ValidationGroup="ContactForm"></asp:RequiredFieldValidator>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="button-box">
                                                <input id="form_botcheck" name="form_botcheck" class="form-control"
                                                    type="hidden" value="">

                                                 <asp:Button runat="server" ID="inscriptionBtn" ValidationGroup="InscriptionForm" Text="Postulez maintenant" CssClass="btn-one" OnClick="inscriptionBtn_Click" />

                                               <%-- <button class="btn-one" type="submit"
                                                    data-loading-text="Please wait...">
                                                    <span class="txt">
                                                        Send Message
                                                    </span>
                                                </button>--%>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                </asp:Panel>
                            </div>
                        </div>
                    </div>
                    <!--End Contact Page Form-->
                   </ContentTemplate>
                  </asp:UpdatePanel>
                </div>
                </div>
            </div>
        </section>
        <!--End Contact Page-->

</asp:Content>
